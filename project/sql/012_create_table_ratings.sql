CREATE TABLE IF NOT EXISTS `Ratings`
(
    id          int auto_increment,
    product_id  int,
    user_id     int,
    rating      int,
    comment     varchar(120),
    modified    TIMESTAMP       default current_timestamp on update current_timestamp,
    created     TIMESTAMP       default current_timestamp,
    primary key (id),
    foreign key (product_id) references Products (id),
    foreign key (user_id) references Users (id)
)
