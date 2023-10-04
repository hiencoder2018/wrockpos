import React from 'react';
import './../../assets/Cart.css'

// Sample cart data
const demoProducts = [
  {
    id: 1,
    name: 'Product 1',
    price: 10.99,
    image: 'http://wrp.test/wp-content/uploads/2023/08/logo-1.jpg',
    qty: 1,
  },
  {
    id: 2,
    name: 'Product 2',
    price: 15.49,
    image: 'http://wrp.test/wp-content/uploads/2023/08/pennant-1.jpg',
    qty: 2,
  },
  {
    id: 3,
    name: 'Product 3',
    price: 8.99,
    image: 'http://wrp.test/wp-content/uploads/2023/08/t-shirt-with-logo-1.jpg',
    qty: 3,
  },
  // {
  //   id: 4,
  //   name: 'Product 4',
  //   price: 8.99,
  //   image: 'http://wrp.test/wp-content/uploads/2023/08/beanie-with-logo-1.jpg',
  //   qty: 3,
  // },
  // {
  //   id: 5,
  //   name: 'Product 5',
  //   price: 8.99,
  //   image: 'http://wrp.test/wp-content/uploads/2023/08/sunglasses-2.jpg',
  //   qty: 1,
  // },
  // {
  //   id: 6,
  //   name: 'Product 6',
  //   price: 8.99,
  //   image: 'http://wrp.test/wp-content/uploads/2023/08/hoodie-with-pocket-2.jpg',
  //   qty: 2,
  // },
  // {
  //   id: 7,
  //   name: 'Product 7',
  //   price: 8.99,
  //   image: 'http://wrp.test/wp-content/uploads/2023/08/hoodie-with-zipper-2.jpg',
  //   qty: 4,
  // },
  // {
  //   id: 8,
  //   name: 'Product 8',
  //   price: 8.99,
  //   image: 'http://wrp.test/wp-content/uploads/2023/08/long-sleeve-tee-2.jpg',
  //   qty: 5,
  // },
  // {
  //   id: 9,
  //   name: 'Product 9',
  //   price: 8.99,
  //   image: 'http://wrp.test/wp-content/uploads/2023/08/tshirt-2.jpg',
  //   qty: 6,
  // },
  // {
  //   id: 10,
  //   name: 'Product 10',
  //   price: 8.99,
  //   image: 'http://wrp.test/wp-content/uploads/2023/08/beanie-2.jpg',
  //   qty: 1,
  // },
  // {
  //   id: 11,
  //   name: 'Product 11',
  //   price: 8.99,
  //   image: 'http://wrp.test/wp-content/uploads/2023/08/belt-2.jpg',
  //   qty: 2,
  // },
];


function Cart() {
  // Implement your cart component here
  return (
    <div className="cart">
      {demoProducts.map(item => (
        <div key={item.id} className="cart-item">
          <img src={item.image} alt={item.name} className="item-image" />
          <div className="item-details">
            <div className="item-properties">
              <span className="property-label">Name:</span>
              <span className="property-value">{item.name}</span>
              <span className="property-label">Price:</span>
              <span className="property-value">${item.price.toFixed(2)}</span>
              <span className="property-label">Quantity: <input /></span>
              <span className="property-value">{item.quantity}</span>
              <span className="property-label">Total:</span>
              <span className="property-value">${(item.price * item.quantity).toFixed(2)}</span>
            </div>
            <button className="delete-button">Delete</button>
          </div>
        </div>
      ))}
    </div>
  );
}

export default Cart;
