@core @core_contentbank @_file_upload @_switch_iframe @javascript
Feature: Confirm content bank events are triggered
  In order remove H5P content from the content bank
  As an admin
  I need to be able to delete any H5P content from the content bank

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And I log in as "admin"
    And I follow "Manage private files..."
    And I upload "h5p/tests/fixtures/filltheblanks.h5p" file to "Files" filemanager
    And I click on "Save changes" "button"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Navigation" block if not present
    Given I expand "Site pages" node
    And I click on "Content bank" "link"
    And I click on "Upload" "link"
    And I click on "Choose a file..." "button"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "filltheblanks.h5p" "link"
    And I click on "Select this file" "button"
    And I click on "Save changes" "button"

  Scenario: Content uploaded event
    Given I click on "Content bank" "link"
    And I click on "Upload" "link"
    And I click on "Choose a file..." "button"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "filltheblanks.h5p" "link"
    And I click on "Select this file" "button"
    And I click on "Save changes" "button"
    And I log out
    When I log in as "admin"
    And I navigate to "Reports > Live logs" in site administration
    Then I should see "Content uploaded"

  Scenario: Content viewed event
    And I click on "Content bank" "link"
    And I click on "filltheblanks.h5p" "link"
    And I log out
    When I log in as "admin"
    And I navigate to "Reports > Live logs" in site administration
    Then I should see "Content viewed"

  Scenario: Content deleted event
    And I click on "Content bank" "link"
    And I click on "filltheblanks.h5p" "link"
    And I open the action menu in "region-main-settings-menu" "region"
    And I choose "Delete" in the open action menu
    And I click on "Delete" "button" in the "Delete content" "dialogue"
    And I log out
    When I log in as "admin"
    And I navigate to "Reports > Live logs" in site administration
    Then I should see "Content deleted"
