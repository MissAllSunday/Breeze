import React, { Children, ReactElement, ReactNode } from 'react';

type TabProps = {
  elementToShow: ReactElement
};

function Tab(props: any): React.ReactElement {
  console.log(props);
  return (<div> lol lol { props.elementToShow }</div>);
}

export default Tab;

