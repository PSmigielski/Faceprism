import React from 'react';
import {useFormik} from 'formik';

export default function ChangePasswordForm() {
    const handleSubmit = () => {

    }
    const validate = (values) => {
        const errors = {};
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
        initialValues: {
            password: '',
            passwordConf: ''
        },
        validate,
        onSubmit: handleSubmit
    })
    return (
        <div className="changePasswordFormWrapper">
            <form className="changePasswordForm">
                <p className="changePasswordParagraph">Przypomnij hasło</p>
                <div className="inputs">
                    <div className="inputContainer">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            className="changeInput"
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
                            className="changeInput"
                            onChange={formik.handleChange}
                            onBlur={formik.handleBlur}
                            value={formik.values.passwordConf}
                            placeholder="Powtórz hasło"
                        />
                        <p className="error">{formik.touched.passwordConf && formik.errors.passwordConf ? formik.errors.passwordConf : null}</p>
                    </div>
                </div>
                <button className="changePasswordSubmit" type="submit">Zmień Hasło</button>
            </form>
        </div>
    )
}
