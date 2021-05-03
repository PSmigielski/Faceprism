import React from 'react'
import {
    BrowserRouter as Router,
    Switch,
    Route,
  } from "react-router-dom";
import ChangePassword from './views/ChangePassword';
import Home from './views/Home';
import RemindPassword from './views/RemindPassword';

const App = () => {
    return (
    <Router>
        <Switch>
          <Route path="/change" component={ChangePassword} />
          <Route path="/remind" component={RemindPassword} />
          <Route path="/" component={Home} />
        </Switch>
    </Router>
    );
}

export default App;