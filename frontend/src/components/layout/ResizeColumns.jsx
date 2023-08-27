import React, { useState } from 'react';

function ResizableColumns({ children }) {
  const [width, setWidth] = useState(300); // Initial width

  const handleResize = (newWidth) => {
    // Implement your resize logic
    setWidth(newWidth);
  };

  return (
    <div className="resizable-column" style={{ width }}>
      {children}
      <div className="resize-handle" onMouseDown={() => handleResize(width - 50)} />
    </div>
  );
}

export default ResizableColumns;
