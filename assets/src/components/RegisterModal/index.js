import React, {useState} from 'react'
import ReactDOM from "react-dom";
import CloseSVG from '../CloseSVG';
import RegisterFirstStep from '../RegisterFirstStep';
import RegisterSecondStep from '../RegisterSecondStep';

export default function RegisterModal({open, onClose}) {
    if(!open) return null;
    const [currentStep, setCurrentStep] = useState(1);
    const [firstStepData, setFirstStepData] = useState({});
    const [secondtStepData, setSecondStepData] = useState({});

    const prevStep = () => {
        setCurrentStep(currentStep <= 1? 1: currentStep - 1)
    }
    const nextStep = () => {
        setCurrentStep(currentStep == 2? 2: currentStep + 1);
    }
    return ReactDOM.createPortal(
        <>
            <div className="registerModalOverlay" />
            <div className="registerModal">
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
                                <circle cx="20" cy="20" r="20" fill={currentStep ==2 ? "#467AFF" : "#C4C4C4"}/>
                            </svg>
                        </div>
                    </nav>
                    {currentStep == 1 ? 
                        <RegisterFirstStep nextStep={nextStep} setFirstStepData={setFirstStepData} currentStep={currentStep}/> :
                        <RegisterSecondStep prevStep={prevStep} setSecondStepData={setSecondStepData} currentStep={currentStep}/>
                    }
                    
                   
                </div>
            </div>
        </>, document.getElementById('portal'));
}
