import React from 'react';

import smfVars from '../DataSource/SMF';

export default function AboutMe(): any {

  return (<div dangerouslySetInnerHTML={{ __html: smfVars.aboutMeContent }}/>);
}

