import React from 'react'
import {useFormik} from "formik"

export default function RegisterSecondStep({prevStep, setSecondStepData}) {
    const handleSubmit = (values) =>{
        setSecondStepData(values);
        handleSubmitEvent();
    }
    const validate = (values) => {

    }
    const formik = useFormik({
        initialValues:{
            password:'',
            email:'',
            passwordConf:'',
        },
        validate,
        onSubmit: handleSubmit
    })

    return (
        <form onSubmit={formik.handleSubmit} className="registerModalStep">
            <input 
                id="email" 
                name="email" 
                type="email" 
                className="registerInput"
                onChange={formik.handleChange}
                value={formik.values.email}
                placeholder="Email"
            />
            <input 
                id="password" 
                name="password" 
                type="password" 
                className="registerInput"
                onChange={formik.handleChange}
                value={formik.values.password}
                placeholder="Hasło"
            />
            <input 
                id="passwordConf" 
                name="passwordConf" 
                type="password" 
                className="registerInput"
                onChange={formik.handleChange}
                value={formik.values.passwordConf}
                placeholder="Powtórz hasło"
            />
            <div className="registerButtonContainer">
                <button className="registerStepButton" onClick={prevStep} type="button">Wróć</button>
                <button className="registerStepButton" type="submit">Zarejestruj się</button>
            </div>
        </form>
    )
}
