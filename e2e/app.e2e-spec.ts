import { NgWaccPage } from './app.po';

describe('ng-wacc App', () => {
  let page: NgWaccPage;

  beforeEach(() => {
    page = new NgWaccPage();
  });

  it('should display welcome message', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('Welcome to app!!');
  });
});
