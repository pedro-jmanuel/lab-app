FROM mysql:8.0.30

ENV MYSQL_ROOT_PASSWORD password
ENV MYSQL_DATABASE labapp_db

RUN apt-get update && apt-get install -y gnupg

COPY database/schema/mysql-schema.sql ./

RUN mysql < mysql-schema.sql


CMD ["mysqld"]
