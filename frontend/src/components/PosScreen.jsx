import React, { useState } from 'react';
import LeftColumn from './layout/LeftColumn';
import RightColumn from './layout/RightColumn';
//import { useQuery } from 'react-query';
//import ResizableColumns from './layout/ResizeColumns';


function PosScreen() {


  return (
    <div className='pos-screen'>  
      <LeftColumn />
      <RightColumn />      
    </div>
  );
}

export default PosScreen;