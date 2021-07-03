import React from 'react'
import { Link } from 'react-router-dom';

const Arrow = (props) => {

    return (
        <Link className="arrowLink" to={props.route}>
            <svg width="72" height="67" viewBox="0 0 72 67" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M63 30.7083H20.49L31.23 20.6862L27 16.75L9 33.5L27 50.25L31.23 46.3138L20.49 36.2917H63V30.7083Z" fill="black"/>
            </svg>        
        </Link>
    )
}
export default Arrow;