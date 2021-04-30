import { useFormik } from "formik";
import React from "react";
import { Link } from "react-router-dom";

const LoginForm = () =>{
    const validate = (values) => {
        //add data validation
    }
    const handleLogin = (values) => {
        //add login handling
    }
    const formik = useFormik({
        initialValues:{
            email: '',
            password: ''
        },
        validate,
        onSubmit: handleLogin
    });

    return (
        <div className="loginFromWrapper">
            <form className="loginForm" onSubmit={formik.handleSubmit}>
                <div className="inputs">
                    <div className="inputContainer">
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            className="loginInput"
                            onChange={formik.handleChange}
                            value={formik.values.email}
                            placeholder="Email"
                        />
                        <p className="error"></p>
                    </div>
                    <div className="inputContainer">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            className="loginInput"
                            onChange={formik.handleChange}
                            value={formik.values.password}
                            placeholder="Hasło"
                        />
                        <p className="error"></p>
                    </div>
                </div>
                <div className="buttonContainer">
                    <button className="loginButton" type="submit">
                        Zaloguj się
                    </button>
                    <div className="buttonContainerSeparator"></div>
                    <button className="registerButton">
                        Zarejestruj się
                    </button>
                </div>
                <p className="loginFormLabel">Zapomniałeś hasło? <Link to="/forget">kliknij tutaj</Link></p>
            </form>
        </div>
    )
}

export default LoginForm;
