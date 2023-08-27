import React from 'react';
import ProductSearch from '../leftColumn/ProductSearch';
import Cart from '../leftColumn/Cart';
import ProductList from '../leftColumn/ProductList';

function LeftColumn() {
  return (
    <div className="left-column">
      <ProductSearch />
      <Cart />      
      <ProductList />
    </div>
  );
}

export default LeftColumn;
