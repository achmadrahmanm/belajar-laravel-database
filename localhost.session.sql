create table counters(
    id VARCHAR(36)  PRIMARY KEY ,
    counter INT NOT NULL DEFAULT 0
) engine innoDB;

insert into counters(id, counter) values('sample', 0);

SELECT * from counters;