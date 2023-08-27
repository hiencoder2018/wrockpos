import React from 'react';
import CustomerSection from '../rightColumn/CustomerSection';
import ButtonsSection from '../rightColumn/ButtonsSection';
import SummarySection from '../rightColumn/SummarySection';

function RightColumn() {
  return (
    <div className='right-column'>
      <CustomerSection />
      <ButtonsSection />
      <SummarySection />
    </div>
  );
}

export default RightColumn;
