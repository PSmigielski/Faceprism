import React,{useRef} from 'react'
import {useFormik} from 'formik'

export default function RegisterFirstStep({nextStep, setFirstStepData, currnetStep}) {
    const datepicker = useRef(null);
    const handleStepChange = (values) =>{
        setFirstStepData(values)
        nextStep();
    }
    const validate = (values) => {

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

    return (
        <form onSubmit={formik.handleSubmit} className="registerModalStep">
            <input 
                id="name" 
                name="name" 
                type="text" 
                className="registerInput"
                onChange={formik.handleChange}
                value={formik.values.email}
                placeholder="Imię"
            />
            <input 
                id="surname" 
                name="surname" 
                type="text" 
                className="registerInput"
                onChange={formik.handleChange}
                value={formik.values.email}
                placeholder="Nazwisko"
            />
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
                            onChange={formik.handleChange}
                        />
                    </div>
                </div>
            </div>
            <input 
                ref={datepicker}
                id="dateOfBirth" 
                name="dateOfBirth" 
                type="text"
                onBlur={()=>formik.values.dateOfBirth == '' ? datepicker.current.type = "text" : datepicker.current.type = "date"}
                onFocus={()=>datepicker.current.type = "date"} 
                className="registerInput"
                onChange={formik.handleChange}
                value={formik.values.email}
                placeholder="Data Urodzenia"
            />
            <button className="registerStepButton" type="submit">Następny etap</button>
        </form> 
    )
}
