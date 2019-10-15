@editor @filter @filter_h5p @core_h5p @_file_upload @_switch_iframe
Feature: Render H5P content using filters
  To write rich text - I need to render H5P content.

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student        |
    And the following "activities" exist:
      | activity | name       | intro      | introformat | course | content  | contentformat | idnumber |
      | page     | PageName1  | PageDesc1  | 1           | C1     | H5Ptest  | 1             | 1        |
    And the "h5p" filter is "on"

  @javascript
  Scenario: Render an external H5P content URL.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "PageName1"
    And I navigate to "Edit settings" in current page administration
    And I set the field "Page content" to "<div>Go for it</div>https://h5p.org/h5p/embed/576651"
    When I click on "Save and display" "button"
    And I wait until the page is ready
    And I switch to "h5p-iframe" class iframe
    Then I should see "Lorum ipsum"

  @javascript
  Scenario: Add an external H5P content URL in a link. Shouldn't be rendered.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "PageName1"
    And I navigate to "Edit settings" in current page administration
    And I set the field "Page content" to "<a href='https://h5p.org/h5p/embed/576651'>Go to https://h5p.org/h5p/embed/576651</a>"
    When I click on "Save and display" "button"
    And I wait until the page is ready
    Then ".h5p-iframe" "css_element" should not exist

  @javascript
  Scenario: Render a server H5P file
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "File" to section "1"
    And I set the following fields to these values:
      | Name                      | H5P     |
    And I upload "filter/h5p/tests/fixtures/ipsums.h5p" file to "Select files" filemanager
    And I press "Save and return to course"
    And I follow "PageName1"
    And I navigate to "Edit settings" in current page administration
    And I click on "Insert H5P" "button" in the "#fitem_id_page" "css_element"
    And I click on "Browse repositories..." "button" in the "Insert H5P" "dialogue"
    And I click on "Server files" "link" in the ".fp-repo-area" "css_element"
    And I click on "H5P (File)" "link"
    And I click on "ipsums.h5p" "link"
    And I click on "Select this file" "button"
    And I click on "Insert H5P" "button" in the "Insert H5P" "dialogue"
    And I wait until the page is ready
    When I click on "Save and display" "button"
    And I switch to "h5pcontent" iframe
    And I switch to "h5p-iframe" class iframe
    Then I should see "Lorum ipsum"
    And I switch to the main frame
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "PageName1"
    And I switch to "h5pcontent" iframe
    And I should not see "you don't have access"

