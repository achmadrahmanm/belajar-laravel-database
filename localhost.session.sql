-- create database belajar_laravel_database;
-- use belajar_laravel_database;
-- create table categories (
--     id int primary key,
--     name varchar(255) not null,
--     description varchar(500) not null,
--     created_at timestamp default current_timestamp,
--     updated_at timestamp default current_timestamp on update current_timestamp
-- );
-- desc categories;
-- create table counters (
--     id varchar(100) not null primary key,
--     counter int not null default 0
-- ) engine innodb;
-- insert into counters(id, counter)
-- values ('sample', 0);
-- select *
-- From counters;
create table products (
    id varchar(100) not null primary key,
    name varchar(100) not null,
    description text null,
    price int not null,
    category_id varchar(100) not null,
    created_at timestamp not null default current_timestamp,
    constraint fk_category_id foreign key (category_id) references categories (id)
) engine innodb;