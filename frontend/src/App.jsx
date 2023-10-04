//import { useState } from 'react'
import PosScreen from './components/PosScreen'
import './assets/App.css'
import './i18n'; // Initialize i18n
import {QueryClient, QueryClientProvider} from 'react-query';
import { ReactQueryDevtools } from 'react-query/devtools'
// Initialze the client
const queryClient = new QueryClient();

function App() {
  
  return (
    <QueryClientProvider client={queryClient}>
        <ReactQueryDevtools initialIsOpen={false} />
        <div className="App">
          <PosScreen />
        </div>
    </QueryClientProvider>    
  );
}

export default App
