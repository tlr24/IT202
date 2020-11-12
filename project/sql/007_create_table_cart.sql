CREATE TABLE Cart
(
    id         int auto_increment,
    product_id int,
    quantity   int,
    user_id    int,
    price      decimal(12, 2) default 0.00,
    created    datetime       default current_timestamp,
    modified   datetime       default current_timestamp on update current_timestamp,
    primary key (id),
    foreign key (product_id) references Products (id),
    foreign key (user_id) references Users (id),
    UNIQUE KEY (product_id, user_id)
)
