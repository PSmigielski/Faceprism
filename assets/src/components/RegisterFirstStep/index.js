import React,{useRef} from 'react'
import {useFormik} from 'formik'

const RegisterFirstStep = React.forwardRef(({nextStep, setFirstStepData}, ref) => {
    const datepicker = useRef(null);
    const handleStepChange = (values) =>{
        setFirstStepData(values)
        nextStep();
    }
    const validate = (values) => {
        const errors = {};

        if (!values.name) {
            errors.name = 'Pole jest wymagane';
        } else if(values.name.length > 30) {
            errors.name = 'Imię nie może być dłuże niż 30 znaków';
        } else if(!/^[a-ząęśćźżńółA-ZĄĘŚĆŻŹŃÓŁ]{1,}$/i.test(values.name)) {
            errors.name = 'Imię zawiera niedozwolone znaki';
        }

        if (!values.surname) {
            errors.surname = 'Pole jest wymagane';
        } else if(values.surname.length > 100) {
            errors.surname = 'Nazwisko nie może być dłuże niż 100 znaków';
        } else if(!/^[a-ząęśćźżńółA-ZĄĘŚĆŻŹŃÓŁ]{1,}$/i.test(values.surname)){
            errors.surname = 'Nazwisko zawiera niedozwolone znaki';
        }

        if(!values.dateOfBirth){
            errors.dateOfBirth = 'Pole jest wymagane'; 
        } else if(!/^\d{4}-\d{2}-\d{2}$/.test(values.dateOfBirth)){
            errors.dateOfBirth = 'Data nie spełnia formatu YYYY-MM-DD';
        }
        return errors;
    }
    const formik = useFormik({
        initialValues:{
            name:'',
            surname:'',
            gender:'',
            dateOfBirth:''
        },
        validate,
        onSubmit: handleStepChange
    })
    const setMaxDate = () => {
        const date = new Date();
        return `${date.getFullYear()}-${date.getMonth()+1 < 10 ? `0${date.getMonth()+1}` : date.getMonth()+1}-${date.getDate() < 10 ? `0${date.getDate()}` : date.getDate()}`
    }
    return (
        <form onSubmit={formik.handleSubmit} className="registerModalStep" ref={ref}>
            <div className="inputContainer">
                <input 
                    id="name" 
                    name="name" 
                    type="text" 
                    className="registerInput"
                    onChange={formik.handleChange}
                    onBlur={formik.handleBlur}
                    value={formik.values.email}
                    placeholder="Imię"
                />
                <p className="error">{formik.touched.name && formik.errors.name ? formik.errors.name : null}</p>
            </div>
            <div className="inputContainer">
                <input 
                    id="surname" 
                    name="surname" 
                    type="text" 
                    className="registerInput"
                    onChange={formik.handleChange}
                    onBlur={formik.handleBlur}
                    value={formik.values.email}
                    placeholder="Nazwisko"
                />
                <p className="error">{formik.touched.surname && formik.errors.surname? formik.errors.surname : null}</p>
            </div>
            <div className="registerRadiosContainer">
                <p className="registerRadioLabel">Płeć:</p>
                <div className="radios">
                    <div className="registerRadioContainer">
                        <label className="registerRadioLabel">mężczyzna</label> 
                        <input 
                            id="gender" 
                            name="gender" 
                            type="radio" 
                            value="male"
                            className="registerRadioInput"
                            onBlur={formik.handleBlur}
                            onChange={formik.handleChange}
                        />
                    </div>
                    <div className="registerRadioContainer">
                        <label className="registerRadioLabel">kobieta</label>
                        <input 
                            id="gender" 
                            name="gender" 
                            type="radio" 
                            value="female"
                            className="registerRadioInput"
                            onBlur={formik.handleBlur}
                            onChange={formik.handleChange}
                        />
                    </div>
                </div>
            </div>
            <div className="inputContainer">
                <input 
                    ref={datepicker}
                    id="dateOfBirth" 
                    name="dateOfBirth" 
                    type="text"
                    onBlur={()=>formik.values.dateOfBirth == '' ? datepicker.current.type = "text" : datepicker.current.type = "date"}
                    onFocus={()=>datepicker.current.type = "date"} 
                    className="registerInput"
                    onChange={formik.handleChange}
                    onBlur={formik.handleBlur}
                    value={formik.values.email}
                    placeholder="Data Urodzenia"
                    max={setMaxDate()}
                />
                <p className="error">{formik.touched.dateOfBirth && formik.errors.dateOfBirth ? formik.errors.dateOfBirth : null}</p>
            </div>
            <button className="registerStepButton" type="submit">Następny etap</button>
        </form> 
    )
});
export default RegisterFirstStep;