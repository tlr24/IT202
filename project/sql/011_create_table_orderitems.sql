CREATE TABLE IF NOT EXISTS `OrderItems`
(
    id          int auto_increment,
    unit_price  decimal(10, 2) default 0.00,
    quantity    int,
    order_id    int,
    product_id  int,
    modified    TIMESTAMP       default current_timestamp on update current_timestamp,
    created     TIMESTAMP       default current_timestamp,
    primary key (id),
    foreign key (order_id) references Orders (id),
    foreign key (product_id) references Products (id)
)
