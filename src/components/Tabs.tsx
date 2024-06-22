import React, { Children, ReactNode, useCallback, useEffect, useState } from 'react';

import smfTextVars from '../DataSource/Txt';

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
    const tabsNames: string[] = [
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

  const changeTab = useCallback((clickedTab: TabType) => {
    if (clickedTab.active) {
      return;
    }
    const currentActiveTab: TabType[] = tabs.filter(tab => tab.active);
    currentActiveTab.forEach((tab: TabType) => {
      tab.active = false;
    });
    clickedTab.active = true;
    currentActiveTab.push(clickedTab);
    currentActiveTab.sort((a, b) => a.index > b.index ? 1 : -1);

    setTabs(currentActiveTab);
  }, [tabs]);

  return <>
    <div id="Breeze_tabs" className="generic_menu">
      <ul className="dropmenu breezeTabs">
        {tabs.map((tab: TabType) => (
          <li className="subsections" key={tab.href}>
            <a href={tab.href} className={tab.active ? 'active' : ''} onClick={() => changeTab(tab)}>{ tab.name }</a>
          </li>
        ))}
      </ul>
    </div>
    <ul>{ tabs.map((tab: TabType) => (
      <li key={tab.index} id={'#tab-' + tab.index} className={tab.active ? 'show' : 'hide'}>{tab.contentElement}</li>
    )) }</ul>
  </>;
}

export default Tabs;

