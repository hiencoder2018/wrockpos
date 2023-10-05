import React, {useState, useEffect} from 'react';
import { useTranslation } from 'react-i18next';
import { useQuery } from 'react-query';

const fetchProducts = async({ queryKey }) => {
  const [ _, currentPage ] = queryKey;
  const res = await fetch(ajaxurl+`?action=getProducts&currentPage=${currentPage}`, {
    dataType: 'jsonp',
    headers: {
       'Accept': 'application/json',
       'Content-Type': 'application/json'
    }
  });
  return res.json();
}


function ProductList() {

  const [currentPage, setCurrentPage] = useState(1);
  const { data, status }  = useQuery(['listProduct', currentPage],fetchProducts);
  

  if (status == 'loading') return <div>Loading....</div>;  
  
  const result = data.data;
  const products = Object.values(result.products)
  const totalPages = result.total_page;
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
          <p dangerouslySetInnerHTML={ { __html: product.price }}></p>
        </div>
      ))}

      <div className='clear'></div>
            
      <div className="pagination">
        {Array.from({ length: totalPages }).map((_, index) => (
          
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
