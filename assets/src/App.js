import React from 'react'
import {
    BrowserRouter as Router,
    Switch,
    Route,
  } from "react-router-dom";
import Home from './views/Home';
import RemindPassword from './views/RemindPassword';

const App = () => {
    return (
    <Router>
        <Switch>
          <Route path="/remind">
            <RemindPassword />
          </Route>
          <Route path="/">
            <Home />
          </Route>
        </Switch>
    </Router>
    );
}

export default App;