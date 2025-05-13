import { TabContentProps } from 'breezeTypes';
import React from 'react';

import smfVars from '../DataSource/SMF';

export default function Tab(props: TabContentProps): React.JSX.Element {

  return (
    <div className="windowbg">
      <div dangerouslySetInnerHTML={{ __html: props.content }} className="content"/>
    </div>
  );
}

