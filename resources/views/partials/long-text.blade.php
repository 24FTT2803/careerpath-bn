{{--
    Long words, links or names that people type (for example
    "aaaaaaaaaaaaaaaa..." or a pasted web address) wrap inside their
    box instead of spilling out of it or stretching the page.

    These rules only take effect when a word is too long to fit, so
    normal text looks exactly the same as before.

    Included at the end of the student layout and the staff (admin
    and lecturer) layout, after each page's own styles.
--}}
<style>
    /* Everywhere: break a word only when it cannot fit on its line */
    body main,
    body .admin-main {
        overflow-wrap: break-word;
    }

    /*
     * Boxes inside rows and grids are allowed to be narrower than
     * the longest word, so the text inside them can wrap.
     */
    body .fact-tile > span,
    body .info-row .value,
    body .project-card,
    body .cert-name,
    body .cert-org,
    body .milestone-item .left,
    body .milestone-item .info,
    body .message-bubble,
    body .notif-item .content,
    body .settings-sidebar .user-info,
    body .student-card-info,
    body .user-card .user-info,
    body .list-row-title,
    body .activity-content,
    body .cpbn-custom-chip > span {
        min-width: 0;
    }

    /* Text people type in: wrap long words anywhere */
    body .dashboard-greeting .greeting-line,
    body .fact-tile > span,
    body .info-row .value,
    body .project-card h4,
    body .project-card .role,
    body .project-card .desc,
    body .project-card .achievement,
    body .tech-tags span,
    body .cert-name,
    body .cert-org,
    body .milestone-item .title,
    body .milestone-item .desc,
    body .milestone-item .notes,
    body .message-bubble,
    body .message-bubble p,
    body .lab-note h2,
    body .lab-body,
    body .notif-item .title,
    body .notif-item .message,
    body .settings-sidebar .name,
    body .page-header h1,
    body .student-hero h2,
    body .student-card-info h4,
    body .user-card .user-info h4,
    body .list-row-title,
    body .activity-message,
    body .cpbn-custom-chip > span {
        overflow-wrap: anywhere;
    }

    /*
     * The lecturer's student detail page: a long word wraps inside
     * its table cell. Kept to this page on purpose; on list tables
     * it would squeeze normal names and emails on a phone.
     */
    body .lecturer-student-detail .admin-table td {
        overflow-wrap: anywhere;
    }

    /*
     * Student names in the lecturer's list: anything wider than
     * 260px wraps instead of stretching the table off the screen.
     * Normal names are far shorter, so they are not affected.
     */
    body .lecturer-student-list .admin-table .cell-title {
        max-width: 260px;
        overflow-wrap: break-word;
    }

    /* Admin dashboard: the activity feed may be narrower than a long word */
    body .admin-dashboard .dashboard-grid > .activity-feed {
        min-width: 0;
    }

    /* Rounded tags and chips never grow wider than their box */
    body .tag,
    body .cpbn-custom-chip,
    body .tech-tags span {
        max-width: 100%;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    /* Status pills stay on one line; a very long one ends in "..." */
    body .status-pill {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
