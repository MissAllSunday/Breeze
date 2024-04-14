import React from 'react';

const ToolTip = (props: { message: string, type: string }) => {

  return (
    <div className={props.type + 'box'}>
      {props.message}
    </div>
  );
};

export default ToolTip;
