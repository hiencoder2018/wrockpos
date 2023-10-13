import React from 'react';
import './../../assets/Cart.css'

// Sample cart data
const demoProducts = [
  {
    id: 1,
    name: 'Product 1',
    reference: 'demo_1',
    stock: 100,
    price: 10.99,
    image: 'http://wrp.test/wp-content/uploads/2023/08/logo-1.jpg',
    qty: 1,
  },
  {
    id: 2,
    name: 'Product 2',
    reference: 'demo_2',
    stock: 200,
    price: 15.49,
    image: 'http://wrp.test/wp-content/uploads/2023/08/pennant-1.jpg',
    qty: 2,
  },
  {
    id: 3,
    name: 'Product 3',
    reference: 'demo_3',
    stock: 100,
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
        <table class="bordered-row">
            <thead>
                <tr>
                    <th width="40%" class="product-name">Name</th>
                    <th class="qty">Qty</th>
                    <th class="unit-price">
                        U/P
                        <span>
                            &nbsp;<i class="fa fa-info-circle" aria-hidden="true" title="Tax incl."></i>
                        </span>
                    </th>
                    <th class="discount">
                        Disc(%)
                    </th>
                    <th class="total">Total</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                {demoProducts.map(item => (
                    <tr class="cart-product-12 product-highlight">
                        <td class="text-left product-name">
                            <p>
                                <span class="product-name">{item.name}</span>
                                <span class="product-info">ID: {item.id} - Ref: {item.reference} - Stock: {item.stock}</span>
                            </p>
                        </td>
                        <td class="qty">
                            <input type="text" size="3" name="quantity" class="qty" value={item.qty}></input>
                        </td>
                        <td class="unit-price">
                            <span>
                                <input type="text" size="5" name="priceWithReduction" class="unit-price" value={item.price.toFixed(2)}></input>
                            </span>

                        </td>
                        <td class="discount">
                            <input type="text" size="3" name="reduction" class="discount" value="0"></input>
                        </td>
                        <td class="text-right total">${(item.price * item.qty).toFixed(2)}</td>
                        <td class="text-center">
                            <a tabindex="-1" href="javascript:void(0);" title="Delete this item" class="delete">x</a>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>       
    </div>
  );
}
export default Cart;
