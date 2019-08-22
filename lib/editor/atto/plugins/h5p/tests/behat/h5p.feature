@editor @editor_atto @atto @atto_h5p @_file_upload
Feature: Add h5ps to Atto
  To write rich text - I need to add h5ps.

  Background:
    Given I log in as "admin"
    And I follow "Profile" in the user menu
    And I follow "Blog entries"
    And I follow "Add a new entry"
    And I set the field "Blog entry body" to "<p>H5P test</p>"
    And I select the text in the "Blog entry body" Atto editor
    And I set the field "Entry title" to "H5P Accordion"
    And I click on "Insert h5p" "button"

  @javascript
  Scenario: Insert a h5p as a file
    Given I set the field "Enter URL" to "https://h5p.org/h5p/embed/576651"
    And I wait until the page is ready
    And I click on "Insert h5p" "button" in the "H5P properties" "dialogue"
    When I click on "Save changes" "button"
    Then "Lorum ipsum" "text" should exist in the "#fitem_id_summary_editor" "css_element"