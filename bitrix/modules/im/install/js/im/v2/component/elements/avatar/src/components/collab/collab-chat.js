import { Color } from 'im.v2.const';

import { AvatarSize } from '../../const/const';
import { BaseUiAvatar, AvatarType } from '../base/base-ui-avatar';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CollabChatAvatar = {
	name: 'CollabChatAvatar',
	components: { BaseUiAvatar },
	props: {
		dialogId: {
			type: [String, Number],
			default: 0,
		},
		size: {
			type: String,
			default: AvatarSize.M,
		},
		withAvatarLetters: {
			type: Boolean,
			default: true,
		},
		customSource: {
			type: String,
			default: '',
		},
		withSpecialTypes: {
			type: Boolean,
			default: true,
		},
		withSpecialTypeIcon: {
			type: Boolean,
			default: true,
		},
		withTooltip: {
			type: Boolean,
			default: true,
		},
	},
	computed: {
		AvatarType: () => AvatarType,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogName(): string
		{
			return this.dialog.name;
		},
		dialogAvatarUrl(): string
		{
			return this.dialog.avatar;
		},
		collabBackgroundColor(): string
		{
			return Color.collab60;
		},
	},
	template: `
		<BaseUiAvatar
			:type="AvatarType.collab"
			:key="dialogId"
			:title="dialogName" 
			:size="size" 
			:url="dialogAvatarUrl" 
			:backgroundColor="collabBackgroundColor" 
		/>
	`,
};
