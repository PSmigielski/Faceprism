import React from 'react'
import Logo from '../Logo';
import logoPath from '../../../images/default.png';

const Navbar = () => {
    const style = {
        backgroundSize:'contain',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
        backgroundImage: `url(${logoPath})`
    }
    return (
        <div className="navbarWrapper">
            <div className="navElementsWrapper">
                <div className="searchBar">
                    <Logo minimal={true} />
                    <input 
                        type="text"
                        className="navbarInput"
                        placeholder="Szukaj"
                    />
                </div>
                <div className="menu">
                    <button className="navItem" style={style}/>
                    <button className="navItem">
                        <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M44.8 16.0269H19.2C17.44 16.0269 16.016 17.4682 16.016 19.2298L16 48.0565L22.4 41.6506H44.8C46.56 41.6506 48 40.2092 48 38.4476V19.2298C48 17.4682 46.56 16.0269 44.8 16.0269Z" fill="#201F1F"/>
                        </svg>
                    </button>
                    <button className="navItem">
                        <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32 48C33.7875 48 35.25 46.5231 35.25 44.7179H28.75C28.75 46.5231 30.1962 48 32 48ZM41.75 38.1538V29.9487C41.75 24.9108 39.085 20.6933 34.4375 19.5774V18.4615C34.4375 17.0995 33.3488 16 32 16C30.6512 16 29.5625 17.0995 29.5625 18.4615V19.5774C24.8988 20.6933 22.25 24.8944 22.25 29.9487V38.1538L19 41.4359V43.0769H45V41.4359L41.75 38.1538Z" fill="#201F1F"/>
                        </svg>
                    </button>
                    <button className="navItem">
                        <svg width="22" height="14" viewBox="0 0 22 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.5119 13.2543C11.7143 14.1752 10.2857 14.1752 9.48814 13.2543L0.875566 3.30931C-0.246186 2.01402 0.673919 1.78814e-06 2.38742 1.78814e-06L19.6126 1.78814e-06C21.3261 1.78814e-06 22.2462 2.01402 21.1244 3.30931L12.5119 13.2543Z" fill="black"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    )
}
export default Navbar;