import React from 'react'
import Arrow from '../../components/Arrow'
import Logo from '../../components/Logo'
import RemindPasswordForm from '../../components/RemindPasswordForm'

const RemindPassword = () => {
    return (
        <div className="remindPasswordWrapper">
            <Arrow route="/"/>
            <Logo />
            <RemindPasswordForm />
        </div>
    )
}
export default RemindPassword