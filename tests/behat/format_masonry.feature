@ewallah @format @format_masonry @javascript

Feature: format_masonry subsections
  In order to view my course contents with subsections, I have to browse

  Background:
    Given the site is running Moodle version 4.5 or higher
    When the following "courses" exist:
      | fullname | shortname | format  | coursedisplay | numsections |
      | Course 1 | C1        | masonry | 1             | 5           |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity   | name        | intro                     | course | idnumber  | section |
      | subsection | Subsection1 | sub1                      | C1     | sub1      | 1       |
      | page       | Page1       | test page                 | C1     | page1     | 1       |
      | subsection | Subsection2 | sub2                      | C1     | sub2      | 1       |
      | lesson     | Lesson1     | Test lesson description   | C1     | lesson1   | 1       |
      | book       | Book1       | Test book description     | C1     | book1     | 2       |

  Scenario: Students do see masonry topics subsections
    Given I am on the "C1" "Course" page logged in as "student1"
    Then I should not see "General" in the ".course-content" "css_element"
    And I should not see "Book 1" in the ".course-content" "css_element"

  Scenario: The modules in the subsections should be rendered correctly in masonry format
    Given I am on the "C1" "Course" page logged in as "teacher1"
    Then I should see "Book1"
    And I should see "Lesson1"
    And I should see "Page1"
    And I log out
    When I am on the "C1" "Course" page logged in as "student1"
    Then I should see "Book1"
    And I should see "Lesson1"
    And I should see "Page1"
