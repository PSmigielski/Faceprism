import React, {useRef, useState} from 'react'
import ReactDOM from "react-dom";
import gsap from 'gsap';
import CloseSVG from '../CloseSVG';
import RegisterFirstStep from '../RegisterFirstStep';
import RegisterSecondStep from '../RegisterSecondStep';

const RegisterModal = React.forwardRef(({open, onClose}, ref) => {
    if(!open) return null;
    const [firstStepData, setFirstStepData] = useState({});
    const handleSubmitEvent = (secondStepData) =>{
        console.log({...firstStepData,...secondStepData});
    }
    const firstStepRef = useRef(null);
    const secondStepRef = useRef(null);
    const circleRef = useRef(null);
    const initalStyle = {
        opacity: 0,
        visibility:"hidden",
    }
    const prevStep = () => {
        const tl = gsap.timeline({defaults: {ease: 'power3.out'}})
        tl.fromTo(secondStepRef.current, {xPercent:0, opacity:1, duration:1}, {xPercent:100, opacity:0, display:'none', duration:1},'-=0.0001')
        .fromTo(firstStepRef.current, {xPercent:-100, opacity:0}, {xPercent:0,opacity:1,display:'flex', duration: 1},'-=0.0001').to(circleRef.current, {fill:'#C4C4C4'},"-=2")
    }
    const nextStep = () => {
        const tl = gsap.timeline({defaults: {ease: 'power3.out'}})
        tl.fromTo(firstStepRef.current, {xPercent:0, opacity:1, duration:1}, {xPercent:-100,opacity:0,display:'none', duration: 1}, '-=0.0001')
        .fromTo(secondStepRef.current, {xPercent:100, opacity:0}, {xPercent:0, opacity:1, display:'flex', duration:1}, '-=0.0001').to(circleRef.current, {fill:'#467AFF'},"-=2")
    }
    return ReactDOM.createPortal(
        <>
            <div className="registerModalOverlay" ref={ref[1]} style={initalStyle} />
            <div className="registerModal" ref={ref[0]} style={initalStyle}>
                {/* 10 20 60 10 */}
                <div className="registerFormWrapper">
                    <div className="registerModalHeader">
                        <p className="registerModlalHeaderParagraph">Zarejestruj się</p>
                        <div className="closeSVGWrapper" onClick={onClose}>
                            <CloseSVG  />
                        </div>
                    </div>
                    <nav className="registerModalNav">
                        <div className="registerModalNavEl">
                            <p className="registerModalNavElParagraph">Dane osobowe</p>
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="20" cy="20" r="20" fill="#467AFF"/>
                            </svg>
                        </div>
                        <div className="registerModalNavEl">
                            <p className="registerModalNavElParagraph">Dane Konta</p>
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="20" cy="20" r="20" fill="#C4C4C4" ref={circleRef}/>
                            </svg>
                        </div>
                    </nav> 
                        <RegisterFirstStep ref={firstStepRef} nextStep={nextStep} setFirstStepData={setFirstStepData} /> 
                        <RegisterSecondStep ref={secondStepRef} prevStep={prevStep} handleSubmitEvent={handleSubmitEvent} />
                    
                   
                </div>
            </div>
        </>, document.getElementById('portal'));
});
export default RegisterModal;