import React from 'react';
import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
import Navbar from './components/Navbar.jsx';
import Footer from './components/Footer.jsx';
import SimpleFooter from './components/SimpleFooter.jsx';
import Home from './pages/Home.jsx';
import Properties from './pages/Properties.jsx';
import Services from './pages/Services.jsx';
import Contact from './pages/Contact.jsx';
import Management from './pages/Management.jsx';
import Login from './pages/Login.jsx';
import AdminLogin from './pages/AdminLogin.jsx';
import Admin from './pages/Admin.jsx';
import Guest from './pages/Guest.jsx';
import PropertyDetails from './pages/PropertyDetails.jsx';

// Layout component to handle conditional footer rendering
function AppLayout() {
  const location = useLocation();
  const isHome = location.pathname === '/';

  return (
    <div className="flex flex-col min-h-screen">
      <Navbar />
      <main className="flex-grow">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/homes" element={<Properties />} />
          <Route path="/services" element={<Services />} />
          <Route path="/contact" element={<Contact />} />
          <Route path="/management" element={<Management />} />
          <Route path="/login" element={<Login />} />
          <Route path="/admin-login" element={<AdminLogin />} />
          <Route path="/admin" element={<Admin />} />
          <Route path="/guest" element={<Guest />} />
          <Route path="/property-details/:id" element={<PropertyDetails />} />
          <Route path="/property-details" element={<PropertyDetails />} />
        </Routes>
      </main>
      {isHome ? <SimpleFooter /> : <Footer />}
    </div>
  );
}

function App() {
  return (
    <Router>
      <AppLayout />
    </Router>
  );
}

export default App;
