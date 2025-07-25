import { Core } from 'im.v2.application.core';
import { ChatType, OwnMessageStatus, UserType, AnchorType } from 'im.v2.const';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelRecentItem, ImModelUser, ImModelMessage } from 'im.v2.model';

const StatusIcon = {
	none: '',
	like: 'like',
	sending: 'sending',
	sent: 'sent',
	viewed: 'viewed',
};

// @vue/component
export const MessageStatus = {
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		recentItem(): ImModelRecentItem
		{
			return this.item;
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.recentItem.dialogId, true);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
		},
		message(): ImModelMessage
		{
			return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
		},
		isChatWithReactions(): boolean
		{
			return this.$store.getters['messages/anchors/isChatHasAnchorsWithType'](this.dialog.chatId, AnchorType.reaction);
		},
		showLike(): boolean
		{
			/*
			* 'this.recent Item.liked' is left to allow work without anchors
			* */
			return this.isChatWithReactions || this.recentItem.liked;
		},
		messageStatus(): $Values<typeof OwnMessageStatus>
		{
			if (this.message.sending)
			{
				return OwnMessageStatus.sending;
			}

			if (this.message.viewedByOthers)
			{
				return OwnMessageStatus.viewed;
			}

			return OwnMessageStatus.sent;
		},
		statusIcon(): $Values<typeof StatusIcon>
		{
			if (this.isSelfChat || this.isBot)
			{
				return StatusIcon.none;
			}

			if (this.showLike)
			{
				return StatusIcon.like;
			}

			if (!this.isLastMessageAuthor || this.needsBirthdayPlaceholder || this.hasDraft)
			{
				return StatusIcon.none;
			}

			return this.messageStatus;
		},
		isLastMessageAuthor(): boolean
		{
			return this.message?.authorId === Core.getUserId();
		},
		isSelfChat(): boolean
		{
			return this.isUser && this.user.id === Core.getUserId();
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isBot(): boolean
		{
			if (this.isUser)
			{
				return this.user.type === UserType.bot;
			}

			return false;
		},
		hasDraft(): boolean
		{
			return Boolean(this.recentItem.draft.text);
		},
		needsBirthdayPlaceholder(): boolean
		{
			if (!this.isUser)
			{
				return false;
			}

			return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
		},
	},
	template: `
		<div class="bx-im-list-recent-item__status-icon" :class="'--' + statusIcon"></div>
	`,
};
