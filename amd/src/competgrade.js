// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JS for the CompetGrade Grading App UI.
 *
 * @module     mod_competgrade/competgrade
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Templates from 'core/templates';

class CompetGrade {

    /**
     * Constructor.
     */
    constructor() {
        this.gradingApp = document.querySelector('[data-region="grading-app"]');
        this.competgrade = this.gradingApp.dataset.competgradeId;
        this.globalGrade = this.gradingApp.querySelector('[data-action="globalgrade"]');
        this.globalComment = this.gradingApp.querySelector('[data-action="globalcomment"]');
        this.userlist = [];
        this.currentUser = 0;
        this.addEventListeners();
        this.getData();
    }

    /**
     * Get the User list.
     * @return {Promise} The promise.
     */
    getUserList() {
        const args = {
            'competgrade': this.competgrade,
        };
        const request = {
            methodname: 'mod_competgrade_userlist',
            args: args
        };
        const promise = Ajax.call([request])[0];
        promise.fail(Notification.exception);
        return promise;
    }

    /**
     * Get the User Comments.
     * @param {Object} user The user.
     * @return {Promise} The promise.
     */
    getUserComments(user) {
        const args = {
            'competgrade': this.competgrade,
            'userid': user.id,
        };
        const request = {
            methodname: 'mod_competgrade_usercomments',
            args: args
        };
        const promise = Ajax.call([request])[0];
        promise.fail(Notification.exception);
        return promise;
    }

    /**
     * Get the Certification.
     * @param {Object} user The user.
     * @return {Promise} The promise.
     */
    getCertification(user) {
        const args = {
            'competgrade': this.competgrade,
            'userid': user.id,
        };
        const request = {
            methodname: 'mod_competgrade_certification',
            args: args
        };
        const promise = Ajax.call([request])[0];
        promise.fail(Notification.exception);
        return promise;
    }
    /**
     * Save the grade
     * @param {Object} args The grade to save.
     * @return {Promise} The promise.
     */
    saveGrade(args) {
        args.competgrade = this.competgrade;
        args.userid = this.currentUser.id;
        const request = {
            methodname: 'mod_competgrade_grade',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Get a comment
     * @param {Object} args The comment to get.
     * @return {Promise} The promise.
     */
    getComment(args) {
        const request = {
            methodname: 'mod_competgrade_getcomment',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Save a comment
     * @param {Object} args The comment to save.
     * @return {Promise} The promise.
     */
    saveComment(args) {
        args.competgrade = this.competgrade;
        const request = {
            methodname: 'mod_competgrade_comment',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Delete a comment
     * @param {Object} args The comment to delete.
     * @return {Promise} The promise.
     */
    deleteComment(args) {
        const request = {
            methodname: 'mod_competgrade_deletecomment',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Delete the grade
     * @param {Object} args The grade to delete.
     * @return {Promise} The promise.
     */
    deleteGrade(args) {
        args.competgrade = this.competgrade;
        const request = {
            methodname: 'mod_competgrade_deletegrade',
            args: args
        };

        let promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    }

    /**
     * Show the comment saved message.
     * @param {Int} commentid The comment id.
     */
    commentSavedMessage(commentid) {
        const commentRegion = this.gradingApp.querySelector('[data-region="comment"][data-comment-id="' + commentid + '"]');
        if (!commentRegion) {
            return;
        }
        const savedMessage = commentRegion.closest('[data-region="commentwrapper"]').querySelector('[data-region="commentsaved"]');
        if (!savedMessage) {
            return;
        }

        savedMessage.classList.remove('opacity-0');
        setTimeout(() => {
            savedMessage.classList.add('opacity-0');
        }, 3000);
    }

    /**
     * Event listeners.
     */
    addEventListeners() {
        this.globalGrade.addEventListener('change', (event) => {
            let value = event.target.value;

            const args = {
                'competgrade': this.competgrade,
                'gradeid': event.target.dataset.gradeId,
                'criterium': event.target.dataset.criteriumId,
            };
            if (!value || value == '') {
                this.deleteGrade(args);
                return;
            }
            args.grade = value;
            this.saveGrade(args).then((response) => {
                if (response.gradeid) {
                    event.target.dataset.gradeId = response.gradeid;
                }
                this.currentUser.grade = value;
                return;
            }).catch(Notification.exception);

        });

        this.globalComment.addEventListener('input', (event) => {
            let value = event.target.value;

            const args = {
                'competgrade': this.competgrade,
                'gradeid': event.target.dataset.gradeId,
                'userid': this.currentUser.id,
                'type': 2,
                'commenttitle': 'Global comment',
                'commenttext': value,
            };
            if (event.target.dataset.commentId) {
                args.commentid = event.target.dataset.commentId;
            }
            if (!value || value == '') {
                if (event.target.dataset.commentId) {
                    this.deleteComment({commentid: event.target.dataset.commentId});
                }
            } else {
                // Repeat the saveComment with a debounce.
                clearTimeout(this.commentTimeout);
                this.commentTimeout = setTimeout(() => {
                    args.commenttext = event.target.value;
                    this.saveComment(args).then((response) => {
                        if (response.commentid) {
                            event.target.dataset.commentId = response.commentid;
                            this.commentSavedMessage(response.commentid);
                        }
                        return;
                    }).catch(Notification.exception);
                }, 1000);
            }
        });

        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-action="prevuser"]')) {
                event.preventDefault();
                let index = this.userlist.indexOf(this.currentUser);
                if (index == 0) {
                    return;
                }
                this.setCurrentUser(this.userlist[index - 1]);
                this.render();
            }
            if (event.target.closest('[data-action="nextuser"]')) {
                event.preventDefault();
                let index = this.userlist.indexOf(this.currentUser);
                if (index == this.userlist.length - 1) {
                    return;
                }
                this.setCurrentUser(this.userlist[index + 1]);
                this.render();
            }
        });
    }

    setCurrentUser(user) {
        this.currentUser = user;
        this.globalGrade.dataset.gradeId = user.gradeid;
        this.globalGrade.value = user.grade;
    }

    /**
     * Render the user list.
     */
    render() {
        this.renderUserNavigation();
        this.renderComments();
        this.renderCertification();
        this.getGlobalComment();
    }

    /**
     * Render the user navigation.
     */
    renderUserNavigation() {
        const navigation = this.gradingApp.querySelector('[data-region="user-navigation"]');
        const template = 'mod_competgrade/usernavigation';
        const context = {
            'user': this.currentUser,
        };
        Templates.render(template, context).then((html) => {
            navigation.innerHTML = html;
            return;
        }).catch(Notification.exception);

        const header = this.gradingApp.querySelector('[data-region="user-header"]');
        const templateHeader = 'mod_competgrade/userheader';
        Templates.render(templateHeader, context).then((html) => {
            header.innerHTML = html;
            return;
        }).catch(Notification.exception);
    }

    /**
     * Render the user comments.
     */
    async renderComments() {
        const comments = await this.getUserComments(this.currentUser);
        const commentsRegion = this.gradingApp.querySelector('[data-region="comments"]');
        const template = 'mod_competgrade/comments';

        if (!comments) {
            return;
        }

        Templates.render(template, comments).then((html) => {
            commentsRegion.innerHTML = html;
            this.activateShowMoreLess();
            return;
        }).catch(Notification.exception);
    }

    /**
     * Render the certification.
     */
    async renderCertification() {
        const certification = await this.getCertification(this.currentUser);
        const certificationRegion = this.gradingApp.querySelector('[data-region="certification"]');
        const template = 'mod_competgrade/certification';

        if (!certification) {
            return;
        }

        Templates.render(template, certification).then((html) => {
            certificationRegion.innerHTML = html;
            return;
        }).catch(Notification.exception);
    }

    /**
     * Activate show more / less for comments.
     */
    activateShowMoreLess() {
        const comments = this.gradingApp.querySelectorAll('[data-region="commenttext"]');
        comments.forEach((comment) => {
            const showMore = comment.querySelector('[data-action="showmore"]');
            const showLess = comment.querySelector('[data-action="showless"]');
            const shortText = comment.querySelector('[data-region="shorttext"]');
            const fullText = comment.querySelector('[data-region="fulltext"]');
            if (shortText.innerHTML.length != fullText.innerHTML.length) {
                showMore.classList.remove('d-none');
            }
            showMore.addEventListener('click', (event) => {
                event.preventDefault();
                shortText.classList.add('d-none');
                fullText.classList.remove('d-none');
                showMore.classList.add('d-none');
                showLess.classList.remove('d-none');
            });
            showLess.addEventListener('click', (event) => {
                event.preventDefault();
                shortText.classList.remove('d-none');
                fullText.classList.add('d-none');
                showMore.classList.remove('d-none');
                showLess.classList.add('d-none');
            });
        });
    }

    /**
     * Get the global comment.
     */
    getGlobalComment() {
        const comment = this.gradingApp.querySelector('[data-action="globalcomment"]');
        const args = {
            'competgrade': this.competgrade,
            'userid': this.currentUser.id,
            'type': 2,
        };
        this.getComment(args).then((response) => {
            if (response.commentid) {
                comment.value = response.commenttext;
                comment.dataset.commentId = response.commentid;
            } else {
                comment.value = '';
                comment.dataset.commentId = '';
            }
            return;
        }).catch(Notification.exception);
    }

    /**
     * User navigation.
     */
    async getData() {
        const response = await this.getUserList();
        if (!response.userlist) {
            return;
        }
        this.userlist = response.userlist;
        this.currentUser = response.userlist[0];
        this.setCurrentUser(this.currentUser);
        this.render();
    }
}

/*
 * Initialise the criteria management.
 *
 */
const init = () => {
    new CompetGrade();
};

export default {
    init: init,
};