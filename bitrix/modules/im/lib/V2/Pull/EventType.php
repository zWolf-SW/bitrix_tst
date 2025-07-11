<?php

namespace Bitrix\Im\V2\Pull;

enum EventType: string
{
	case StartWriting = 'startWriting';
	case InputActionNotify = 'inputActionNotify';
	case MessagesAutoDeleteDelayChanged = 'messagesAutoDeleteDelayChanged';
	case ChatFieldsUpdate = 'chatFieldsUpdate';
	case UpdateFeature = 'updateFeature';
	case PromotionUpdated = 'promotionUpdated';
}
