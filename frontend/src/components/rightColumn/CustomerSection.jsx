import React from 'react';

function CustomerSection() {
  // Implement your customer section component here
  return (
    <div className="customer-section">
      <img src="people-icon.png" alt="People" />
      <input type="text" placeholder="Search customer or scan barcode" />
      <button>Add New Customer</button>
    </div>
  );
}

export default CustomerSection;
