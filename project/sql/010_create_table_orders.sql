CREATE TABLE IF NOT EXISTS `Orders`
(
    id          int auto_increment,
    total_price decimal(10, 2) default 0.00,
    address     varchar(150),
    payment_method varchar(40),
    modified    TIMESTAMP       default current_timestamp on update current_timestamp,
    created     TIMESTAMP       default current_timestamp,
    user_id     int,
    primary key (id),
    foreign key (user_id) references Users (id)
)
