import React from 'react'
import {useFormik} from "formik"

const RegisterSecondStep = React.forwardRef(({prevStep, handleSubmitEvent}, ref) => {
    const handleSubmit = (values) => handleSubmitEvent(values)
    const validate = (values) => {
        const errors = {};
        if (!values.email) {
            errors.email = 'Pole jest wymaganie';
        } else if (!/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(values.email)) {
            errors.email = 'Nieprawidłowy adres email';
        }
        if(!values.password){
            errors.password = 'Pole jest wymaganie';
        } else if(!/^(?=.*\d)(?=.*[a-z])(?=.*[\!\@\#\$\%\^\&\*\(\)\_\+\-\=])(?=.*[A-Z])(?!.*\s).{8,}$/.test(values.password)){
            errors.password = 'Hasło powinno się składać z minimum 8 znaków w tym jednej dużej litery, cyfry i znaku specjalnego';
        }
        if(!values.passwordConf){
            errors.passwordConf = 'Pole jest wymaganie';
        } else if(values.password != values.passwordConf){
            errors.passwordConf = 'Hasła nie są do siebie podobne';
        }
        return errors;
    }
    const formik = useFormik({
        initialValues:{
            password:'',
            email:'',
            passwordConf:''
        },
        validate,
        onSubmit: handleSubmit
    })
    const style = {
        display:'none',
        transfom: "translateX(100%)"
    }
    return (
        <form onSubmit={formik.handleSubmit} className="registerModalStep" ref={ref} style={style}>
            <div className="inputContainer">
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    className="registerInput"
                    onChange={formik.handleChange}
                    onBlur={formik.handleBlur}
                    value={formik.values.email}
                    placeholder="Email"
                />
                <p className="error">{formik.touched.email && formik.errors.email ? formik.errors.email : null}</p>
            </div>
            <div className="inputContainer">
                <input 
                    id="password" 
                    name="password" 
                    type="password" 
                    className="registerInput"
                    onChange={formik.handleChange}
                    onBlur={formik.handleBlur}
                    value={formik.values.password}
                    placeholder="Hasło"
                />
                <p className="error">{formik.touched.password && formik.errors.password ? formik.errors.password : null}</p>
            </div>
            <div className="inputContainer">
                <input 
                    id="passwordConf" 
                    name="passwordConf" 
                    type="password" 
                    className="registerInput"
                    onChange={formik.handleChange}
                    onBlur={formik.handleBlur}
                    value={formik.values.passwordConf}
                    placeholder="Powtórz hasło"
                />
                <p className="error">{formik.touched.passwordConf && formik.errors.passwordConf ? formik.errors.passwordConf : null}</p>
            </div>
            <div className="registerButtonContainer">
                <button className="registerStepButton" onClick={prevStep} type="button">Wróć</button>
                <button className="registerStepButton" type="submit">Zarejestruj się</button>
            </div>
        </form>
    )
});

export default RegisterSecondStep;
