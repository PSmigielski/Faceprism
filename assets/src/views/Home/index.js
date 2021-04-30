import React from 'react';
import Hero from "../../components/Hero";
import LoginForm from "../../components/LoginForm";

const Home = () =>{
    return (
    <div className="homeWrapper">
        <div className="homeMain">
            <Hero />
            <LoginForm />
        </div>
        <footer className="homeFooter">
            <p className="homeFooterParagraph">&copy;faceprism 2021</p>
        </footer>
    </div>);
}
export default Home;