import React from 'react'
import {useFormik} from 'formik'

const RemindPasswordForm = () => {
    const handleSubmit = (values) => handleSubmitEvent(values)
    const validate = (values) => {
        const errors = {};
        if (!values.email) {
            errors.email = 'Pole jest wymaganie';
        } else if (!/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(values.email)) {
            errors.email = 'Nieprawidłowy adres email';
        }
        return errors;
    }
    const formik = useFormik({
        initialValues:{
            email:''
        },
        validate,
        onSubmit:handleSubmit
    })
    return (
        <div className="remindPasswordFormWrapper">
            <form className="remindPasswordForm">
                <p className="remindPasswordParagraph">Przypomnij hasło</p>
                <div className="inputContainer">
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        className="remindInput"
                        onChange={formik.handleChange}
                        onBlur={formik.handleBlur}
                        value={formik.values.email}
                        placeholder="Email"
                    />
                    <p className="error">{formik.touched.email && formik.errors.email ? formik.errors.email : null}</p>
                </div>
                <button className="remindPasswordSubmit" type="submit">Wyślij email</button>
            </form>
        </div>
    )
}
export default RemindPasswordForm;