FROM mariadb:11.2.2-jammy

COPY ./config/mariadb/mysql.cnf /etc/mysql/conf.d/mysql.cnf