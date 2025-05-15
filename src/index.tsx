import React from 'react';
import { createRoot } from 'react-dom/client';

import Tab from './components/Tab';
import Tabs from './components/Tabs';
import smfVars from './DataSource/SMF';
import smfTextVars from './DataSource/Txt';
import Wall from './Wall';

const rootElement = (document.getElementById('root') ?? document.createElement('div'));
const root = createRoot(rootElement);
const wallType = rootElement.getAttribute('wallType') ?? 'profile';
// @ts-expect-error settings are loaded server side
const pagination: number = window.breezePagination ?? 5;
// @ts-expect-error settings are loaded server side
const enableBuddiesTab: boolean = Boolean(window.breezeEnableBuddiesTab) ?? false;
// @ts-expect-error settings are loaded server side
const enableAboutMeTab: boolean = Boolean(window.breezeAboutMe) ?? false;

root.render(
    <React.StrictMode>
      <Tabs>
          <Wall wallType={wallType} pagination={pagination} name={smfTextVars.tabs.wall} />
        { enableAboutMeTab && <Tab content={smfVars.aboutMeContent} name={smfTextVars.tabs.about}/> }
        { enableBuddiesTab && <Tab content={smfVars.buddiesTabContent} name={smfTextVars.tabs.buddies}/> }
      </Tabs>
    </React.StrictMode>,
);
