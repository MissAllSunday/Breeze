const tabs = {};
const getCurrentActiveTab = () => {

  let currentTab = null, key;

  for (key in tabs) {
    if (Object.prototype.hasOwnProperty.call(tabs, key)) {
      if (tabs[key].active === true) {
        currentTab = tabs[key];
      }
    }
  }

  return currentTab;
};

const tabChange = function (newTab) {

  let currentActiveTab = getCurrentActiveTab();

  currentActiveTab.active = false;
  currentActiveTab.contentElement.classList.replace('show', 'hide');
  currentActiveTab.tabElement.querySelectorAll(':scope a')[0].classList.remove('active');
  newTab.tabElement.querySelectorAll(':scope a')[0].classList.add('active');

  newTab.contentElement.classList.replace('hide', 'show');
  newTab.active = true;
};

document.querySelectorAll('ul.breezeTabs li.subsections').forEach((element, i) => {
  const elementName = element.getAttribute('id');
  const contentElementId = element.querySelectorAll(':scope a')[0].getAttribute('href').replace('#', '');
  const contentElement =  document.getElementById(contentElementId);


  tabs[elementName] = {
    href : element.querySelectorAll(':scope a')[0].getAttribute('href'),
    name : element.getAttribute('id'),
    active : (elementName === 'wall'),
    tabElement: element,
    contentElement: contentElement,
  };

  element.addEventListener('click', (event) => {

    if (!tabs[elementName].active) {
      tabChange(tabs[elementName]);
    }

    event.preventDefault();
    return false;

  });

});
