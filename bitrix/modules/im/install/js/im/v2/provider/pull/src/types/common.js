import type { StatusGroupName } from 'imopenlines.v2.const';

import type { BotType, UserType } from 'im.v2.const';
import type { RawReaction, RawShortUser } from 'im.v2.provider.service.types';

export type PullExtraParams = {
	im_revision: number,
	is_shared_event: boolean,
	im_revision_mobile: number,
	revision_im_mobile: number,
	revision_im_rest: number,
	revision_im_web: number,
	revision_mobile: number,
	revision_web: number,
	sender: {
		type: number,
		id: string
	},
	server_name: string,
	server_time: string,
	server_time_ago: number,
	server_time_unix: number,
	action_uuid?: string
};

export type RawChat = {
	avatar: string,
	call: string,
	call_number: string,
	color: string,
	date_create: string,
	entity_data_1: string,
	entity_data_2: string,
	entity_data_3: string,
	entity_id: string,
	entity_type: string,
	extranet: boolean,
	id: string,
	manager_list: number[],
	message_count: number,
	message_type: string,
	mute_list: {[userId: string]: boolean},
	name: string,
	owner: string,
	parent_chat_id: number,
	parent_message_id: number,
	public: string,
	type: string,
	backgroundId: string | null,
	textFieldEnabled: boolean
};

export type RawFile = {
	authorId: number,
	authorName: string,
	chatId: number,
	date: string,
	extension: string,
	id: number,
	image: boolean,
	name: string,
	progress: number,
	size: number,
	status: string,
	type: string,
	urlDownload: string,
	urlPreview: string,
	urlShow: string,
	viewerAttrs: {
		actions: string,
		imChatId: number,
		objectId: string,
		src: string,
		title: string,
		viewer: null,
		viewerGroupBy: string,
		viewerType: string
	}
};

export type RawUser = {
	absent: boolean,
	active: boolean,
	avatar: string,
	avatar_id: string,
	birthday: string,
	bot: boolean,
	color: string,
	connector: boolean,
	departments: number[],
	desktop_last_date: string | false,
	externalAuthId: string,
	external_auth_id: string,
	extranet: boolean,
	type: $Values<typeof UserType>,
	firstName: string,
	first_name: string,
	gender: 'M' | 'F',
	id: string,
	idle: boolean,
	lastActivityDate: string,
	last_activity_date: string,
	lastName: string,
	last_name: string,
	mobileLastDate: string,
	mobile_last_date: string,
	name: string,
	network: boolean,
	phone_device: boolean,
	phones: false,
	profile: string,
	services: null,
	status: string,
	tz_offset: number,
	workPosition: string,
	work_position: string,
	bot_data?: {
		app_id: string,
		background_id: string,
		code: string,
		type: $Values<typeof BotType>,
		is_hidden: boolean,
		is_support_openline: boolean,
	}
};

export type RawMessage = {
	authorId: number,
	chatId: number,
	counter: number,
	date: string,
	id: number,
	params: Object,
	prevId: number,
	recipientId: string,
	senderId: number,
	system: string,
	templateFileId: string,
	templateId: string,
	text: string,
	textLegacy: string,
	isImportant: boolean,
	importantFor: number[],
	additionalEntities: {
		additionalMessages: RawMessage[],
		files: RawFile[],
		messages: RawMessage[],
		reactions: RawReaction[],
		users: RawUser[],
		usersShort: RawShortUser[],
	},
};

export type MultipleRawMessage = {
	id: number,
	senderId: number,
	completelyDeleted: boolean,
	params: Object<string, any>,
	text: string,
}

export type RawMultidialog = {
	botId: string,
	dialogId: string,
	chatId: number,
	status: string,
	isSupport?: true,
	dateMessage?: string,
};

export type RawLines = {
	id: number,
	status: number,
	data_create: string,
	operatorId: number,
	statusGroup: StatusGroupName,
	queueId: number,
	pinned: boolean,
	isClosed: boolean,
}
