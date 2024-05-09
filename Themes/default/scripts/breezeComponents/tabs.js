const tabs = {
  wall: {
    href: '#tab-wall',
  },
};
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
  currentActiveTab.contentElement.fadeOut('slow', function () {
    currentActiveTab.tabElement.find('a').removeClass('active');
    newTab.tabElement.find('a').addClass('active');

    newTab.contentElement.fadeIn('slow');
    newTab.active = true;
  });

};
$('ul.breezeTabs li.subsections').each((key, liElement) => {

  const $element = $(liElement);
  const elementName = $element.attr('id');
  const contentElement = $($element.find('a').attr('href'));

  tabs[elementName] = {
    href : $element.find('a').attr('href'),
    name : $element.attr('id'),
    active : (elementName === 'wall'),
    tabElement: $element,
    contentElement: contentElement,
  };

  $element.on('click', false, (e) => {

    if (tabs[elementName].active === true) {
      return false;
    } else {
      tabChange(tabs[elementName]);
    }

    e.preventDefault();
    return false;
  });
});
