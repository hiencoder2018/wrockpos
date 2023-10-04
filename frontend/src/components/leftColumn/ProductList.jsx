import React, {useState, useEffect} from 'react';
import { useTranslation } from 'react-i18next';
import { useQuery } from 'react-query';

const fetchProducts = async() => {
  const res = await fetch(ajaxurl+"?action=getProducts", {
    dataType: 'jsonp',
    headers: {
       'Accept': 'application/json',
       'Content-Type': 'application/json'
    }
  });
  return res.json();
}


function ProductList() {

  const { data, status }  = useQuery('data',fetchProducts);
  
  if (status == 'loading') return <div>Loading....</div>;  
  // console.log('------')
  // console.log(productsToShow)
  const result = data.data;
  const products = Object.values(result)
  console.log(products);
  const itemsPerPage = 3;
  //const [currentPage, setCurrentPage] = useState(1);  
  //const startIndex = (currentPage - 1) * itemsPerPage;
  //const endIndex = startIndex + itemsPerPage;
  // const productsToShow = products.slice(startIndex, endIndex);
  // const totalPages = Math.ceil(products.length / itemsPerPage);
  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= totalPages) {
      setCurrentPage(newPage);
    }
  };
  return (
    <div  className="product-list">
      {products.map(product => (
        <div key={product.id} className="product-item">
          <p dangerouslySetInnerHTML={ { __html: product.image } }></p>
          <p>{product.name}</p>
          <p>{product.price}</p>
        </div>
      ))}

      <div className='clear'></div>
            
      <div className="pagination">
        {Array.from({ length: itemsPerPage }).map((_, index) => (
          
          <button
            key={index}
            
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
