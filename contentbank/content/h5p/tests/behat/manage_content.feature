@core @core_contentbank @contentbank_h5p @_file_upload @javascript
Feature: Manage H5P content from the content bank
  In order to manage H5P content in the content bank
  As an admin
  I need to be able to edit any H5P content in the content bank

  Background:
    Given I log in as "admin"
    And I follow "Manage private files..."
    And I upload "h5p/tests/fixtures/filltheblanks.h5p" file to "Files" filemanager
    And I click on "Save changes" "button"
    And I am viewing content bank
    And I click on "Upload" "link"
    And I click on "Choose a file..." "button"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "filltheblanks.h5p" "link"
    And I click on "Select this file" "button"
    And I click on "Save changes" "button"

  Scenario: Admins can rename content in the content bank
    Given I am viewing content bank
    And I should see "filltheblanks.h5p"
    When I follow "filltheblanks.h5p"
    And I open the action menu in "region-main-settings-menu" "region"
    Then I should see "Rename"
    And I choose "Rename" in the open action menu
    And I set the field "Content name" to "New name"
    And I click on "Rename" "button"
    And I wait until the page is ready
    And I should not see "filltheblanks.h5p"
    And I should see "New name"