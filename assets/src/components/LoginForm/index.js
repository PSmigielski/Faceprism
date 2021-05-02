import { useFormik } from "formik";
import React, {useRef} from "react";
import { Link } from "react-router-dom";
import RegisterModal from "../RegisterModal";
import gsap from "gsap"

const LoginForm = () =>{
    const [isOpen, setIsOpen] = React.useState(false);
    const registerModalRef = useRef(null);
    const registerModalBackgroundRef = useRef(null);
    const handleLogin = () => {
        //xD
    }
    const validate = (values) => {
        let errors = {}
        if(!values.email){
            errors.email = "pole jest wymagane"
        }
        if(!values.password){
            errors.password = "pole jest wymagane"
        }
        return errors;
    }
    const handleModalOpen = () => {
        setIsOpen(true);
        setTimeout(()=>{
            const tl = gsap.timeline({defaults: {ease: 'power3.out'}});
            tl.fromTo(registerModalRef.current, {opacity: 0}, {opacity:1,visibility:"visible", duration:.5})
            .fromTo(registerModalBackgroundRef.current,{opacity: 0}, {opacity:1,visibility:"visible", duration:.5},"-=0.5" );
        }, 100)
    }
    const handleModalClose = () => {
        const tl = gsap.timeline({defaults: {ease: 'power3.out'}});
        tl.fromTo(registerModalRef.current, {opacity: 1}, {opacity:0,duration:.5, onComplete:setIsOpen, onCompleteParams:[false]})
        .fromTo(registerModalBackgroundRef.current,{opacity: 1}, {opacity:0,duration:.5},"-=0.5" );
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
                        <p className="error">{formik.touched.email && formik.errors.email ? formik.errors.email : null}</p>
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
                        <p className="error">{formik.touched.password && formik.errors.password ? formik.errors.password : null}</p>
                    </div>
                </div>
                <div className="buttonContainer">
                    <button className="loginButton" type="submit">
                        Zaloguj się
                    </button>
                    <div className="buttonContainerSeparator"></div>
                    <button className="registerButton" type="button" onClick={handleModalOpen}>
                        Zarejestruj się
                    </button>
                    <RegisterModal ref={[registerModalRef, registerModalBackgroundRef]} open={isOpen} onClose={handleModalClose}/>
                </div>
                <p className="loginFormLabel">Zapomniałeś hasło? <Link to="/forget">kliknij tutaj</Link></p>
            </form>
        </div>
    )
}

export default LoginForm;
