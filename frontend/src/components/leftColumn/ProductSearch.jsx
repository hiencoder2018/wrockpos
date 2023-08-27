import React from 'react';

function ProductSearch() {
  // Implement your product search component here
  return (
    <div className="product-search">
      <select>
        <option value="name">Name</option>
        <option value="id">ID</option>
        <option value="barcode">Barcode</option>
        <option value="description">Description</option>
      </select>
      <input type="text" placeholder="Search product" />      
    </div>
  );
}

export default ProductSearch;
