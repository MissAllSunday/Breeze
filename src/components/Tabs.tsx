import React, {Children, JSXElementConstructor, ReactNode, useCallback, useEffect, useState} from 'react';

import smfTextVars from '../DataSource/Txt';
import {showInfo} from "../utils/tooltip";
import {getStatus, ServerGetStatusResponse} from "../api/StatusApi";

interface TabType {
  index: number;
  href: string;
  name: string;
  active: boolean;
  contentElement: React.ReactElement;
}

type TabsType = TabType[];


function Tabs(props: { children: ReactNode; }): any {

  const [tabs, setTabs] = useState<TabsType>([]);


  useEffect(() => {
    const tabsNames = [
      smfTextVars.tabs.wall,
      smfTextVars.tabs.about,
      smfTextVars.tabs.activity,
    ];
    const initialTabs:TabsType = [];
    Children.forEach(props.children, (child:any, index) => {

      initialTabs.push({
        index,
        href: '#tab-' + index,
        name: tabsNames[index],
        active: index === 0,
        contentElement: child,
      });
    });

    setTabs(initialTabs);
  }, [props.children]);

  const mappedTabs = tabs.map((tab: TabType) => (
    <li className="subsections" key={tab.href}>
      <a href={tab.href} className={tab.active ? 'active' : ''} onClick={() => changeTab(tab)}>{ tab.name }</a>
    </li>
  ));

  const changeTab = useCallback((currentTab: TabType) => {

  }, []);

  const mappedContent = Children.map(props.children, child =>
    <li className="Row">
    {child}
    </li>,
  );

  return <>

    <div id="Breeze_tabs" className="generic_menu">
      <ul className="dropmenu breezeTabs">
        {mappedTabs}
      </ul>
    </div>
    <ul>{ mappedContent }</ul>

  </>;
}

export default Tabs;

