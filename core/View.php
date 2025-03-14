<?php

declare(strict_types=1);

namespace Core;

use function Core\Libs\array_path_exists;
use function Core\Libs\array_path_explode;
use function Core\Libs\array_path_export;
use function Core\Libs\array_path_get;
use function Core\Libs\array_path_remove;

class View implements \ArrayAccess
{
	private string $filename;
	private string $templatePath;
	private string $extension;
	private array $context;
	private string $cachePath;
	private bool $useCache;

	public function __construct(
		?string $templatePath = null,
		?string $extension = null,
		array $context = null,
		?string $cachePath = null,
		?bool $useCache = null
	) {
		$this->templatePath = rtrim($templatePath ?? dirname(__DIR__), DIRECTORY_SEPARATOR);
		$this->extension = trim($extension ?? '.html');
		$this->context = $context ?? [];
		$this->cachePath = rtrim($cachePath ?? sys_get_temp_dir(), DIRECTORY_SEPARATOR);
		$this->useCache = $useCache ?? false;
	}

	public function render(?string $filename = null, array $context = null): void
	{
		$filename = $filename ?? $this->filename;

		$context = array_path_export(array_path_explode('.', $context ?? []));
		$this->context = array_merge_recursive($this->context, $context);

		$filePath = $this->resolveFile($filename);

		require_once($filePath);
	}

	public function getFilename(): string
	{
		return $this->filename;
	}

	public function setFilename(string $filename): void
	{
		$this->filename = $filename;
	}

	private function resolveFile(string $filename): string
	{
		$cachedFilePath = $this->cachePath . DIRECTORY_SEPARATOR . str_replace(['\\', '/'], '_', $filename) . '.php';

		if ($this->useCache && file_exists($cachedFilePath)) {
			return $cachedFilePath;
		}


		$cachedFileDirectoryPath = dirname($cachedFilePath);

		if (!file_exists($cachedFileDirectoryPath)) {
			mkdir($cachedFileDirectoryPath, 0744, true);
		}

		$code = $this->transpile($filename);
		file_put_contents($cachedFilePath, $code);


		return $cachedFilePath;
	}

	private function transpile(string $filename): string
	{
		$code = $this->transpileFiles($filename);
		$code = $this->transpileBlocks($code);
		$code = $this->transpileEchos($code);
		$code = $this->transpilePHP($code);
		$code = $this->transpileVariables($code);

		return $code;
	}

	private function transpileFiles(string $filename): string
	{
		$filePath = $this->templatePath . DIRECTORY_SEPARATOR . $filename . $this->extension;
		$code = file_get_contents($filePath);

		preg_match_all('/{%\s*(extends|include)\s+"(?<filename>[^\']*?)"\s*%}/is', $code, $matches, PREG_SET_ORDER);

		foreach ($matches as $value) {
			$content = $this->transpileFiles($value['filename']);
			$code = str_replace($value[0], $content, $code);
		}

		return $code;
	}

	private function transpileBlocks(string $code): string
	{
		$blocks = [];

		preg_match_all('/\s*{%\s*block\s+(?<name>.*?)\s*%}(?<content>.*?){%\s*endblock\s*%}\s*/is', $code, $matches, PREG_SET_ORDER);

		// Convert @parent to parent block content
		foreach ($matches as $value) {
			$blocks[$value['name']] = preg_replace('/\s*{{\s*@parent\s*}}\s*/is', $blocks[$value['name']] ?? '', $value['content']);
			$code = str_replace($value[0], '', $code);
		}

		do {
			// Convert {{ block name }} to block name content.
			foreach ($blocks as $name => $value) {
				$code = preg_replace("/\s*{{\s*block\s+{$name}\s*}}\s*/is", $value, $code, -1, $count);
			}
		} while ($count > 0);

		// Remove unused blocks
		$code = preg_replace('/\s*{{\s*block\s+(.*?)\s*}}\s*/is', '', $code);

		return $code;
	}

	private function transpileEchos(string $code): string
	{
		// Append fallback values. Ex : {{ $this.undefined }} to {{ $this.undefined ?? '' }}
		$code = preg_replace('/({{[^{}]+?)(\$[\w_\.]+)([^{}]+?}})/is', '$1$2 ?? \'\'$3', $code);

		// Convert {{{ }}} tags to echo expression. Ex : {{{ $this.value }}} to <?php echo $this.value ...
		$code = preg_replace('/{{\s*([^{}]+?)\s*}}/is', '<?php echo $1 ?>', $code);

		return $code;
	}

	private function transpilePHP(string $code): string
	{
		return preg_replace('/{%\s*(.+?)\s*%}/is', '<?php $1 ?>', $code);
	}

	private function transpileVariables(string $code): string
	{
		preg_match_all('/(?<=\<\?php)\s*(?<content>[^\}]+?)\s*(?=\?\>)/is', $code, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			// Convert dot properties array access. Ex : $this.message.content to $this['message']['content']
			$replace = preg_replace('/\.([a-z0-9_]+)/is', '[\'$1\']', $match['content']);
			$code = str_replace($match[1], $replace, $code);
		}

		return $code;
	}

	/**
	 * Implement ArrayAccess's offsetSet method used to set a value in the array.
	 *
	 * @param mixed $offset The index, key or null value representing the position of $value in the array.
	 * @param object  $value The value to store in the array.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value): void
	{
		if (is_null($offset)) {
			$this->context[] = $value;
			return;
		}

		$item = array_path_export(array_path_explode('.', [$offset => $value]));
		$this->context = array_merge_recursive($this->context, $item);
	}

	/**
	 * Implement ArrayAccess's offsetExists method used to check if an offset exists in the array.
	 *
	 * @param string $offset The index, key or null value representing the position of $value in the array.
	 *
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		$keys = explode('.', $offset);

		return array_path_exists($this->context, $keys);
	}

	/**
	 * Implement ArrayAccess's offsetUnset method used to remove a value from the array.
	 *
	 * @param string $offset The index, key or null value representing the position of $value in the array.
	 *
	 * @return void
	 */
	public function offsetUnset($offset): void
	{
		$keys = explode('.', $offset);

		array_path_remove($this->context, $keys);
	}

	/**
	 * Implement ArrayAccess's offsetGet method
	 *
	 * @param string $offset The index, key or null value representing the position of $value in the array.
	 *
	 * @return bool
	 */
	public function offsetGet($offset): mixed
	{
		$keys = explode('.', $offset);

		return array_path_get($this->context, $keys);
	}
}
