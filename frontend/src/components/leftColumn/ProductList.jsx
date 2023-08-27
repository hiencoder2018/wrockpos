import React, {useState} from 'react';

// Sample product data
const demoProducts = [
  {
    id: 1,
    name: 'Product 1',
    price: 10.99,
    image: 'product1.jpg',
  },
  {
    id: 2,
    name: 'Product 2',
    price: 15.49,
    image: 'product2.jpg',
  },
  {
    id: 3,
    name: 'Product 3',
    price: 8.99,
    image: 'product3.jpg',
  },
  {
    id: 4,
    name: 'Product 4',
    price: 8.99,
    image: 'product3.jpg',
  },
  {
    id: 5,
    name: 'Product 5',
    price: 8.99,
    image: 'product3.jpg',
  },
  {
    id: 6,
    name: 'Product 6',
    price: 8.99,
    image: 'product3.jpg',
  },
  {
    id: 7,
    name: 'Product 7',
    price: 8.99,
    image: 'product3.jpg',
  },
  {
    id: 8,
    name: 'Product 8',
    price: 8.99,
    image: 'product3.jpg',
  },
  {
    id: 9,
    name: 'Product 9',
    price: 8.99,
    image: 'product3.jpg',
  },
  {
    id: 10,
    name: 'Product 10',
    price: 8.99,
    image: 'product3.jpg',
  },
  {
    id: 11,
    name: 'Product 11',
    price: 8.99,
    image: 'product3.jpg',
  },
];

function ProductList({ products }) {
  
  const itemsPerPage = 3; // Number of items to display per page
  const [currentPage, setCurrentPage] = useState(1);

  // Calculate the index range for the current page
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;

  // Get the products for the current page
  const productsToShow = demoProducts.slice(startIndex, endIndex);

  const totalPages = Math.ceil(demoProducts.length / itemsPerPage);

  const handlePageChange = (newPage) => {
    console.log('aab')
    if (newPage >= 1 && newPage <= totalPages) {
      setCurrentPage(newPage);
    }
  };

  return (
    <div className="product-list">
      {productsToShow.map(product => (
        <div key={product.id} className="product-item">
          <img src={product.image} alt={product.name} />
          <p>{product.name}</p>
          <p>Price: ${product.price.toFixed(2)}</p>
        </div>
      ))}
      <div className="pagination">
        {Array.from({ length: totalPages }).map((_, index) => (
          <button
            key={index}
            className={index + 1 === currentPage ? 'active' : ''}
            onClick={() => handlePageChange(index + 1)}
          >
            {index + 1}
          </button>
        ))}
      </div>
    </div>
  );
}

export default ProductList;
