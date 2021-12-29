"""
In this script we want to parse out the history of the conversations.
"""

import json
from datetime import datetime
import re

userMap = {
    # removed to protect anonymity
    # mapped Slack usernames to participants
}

refUser = re.compile("<@(\w+)>")


with open("dbdump_conversation_state.20211208.json", "r") as inFile:
    data = json.load(inFile)


columns = [ "Thread Index", "Message Index", "Index", "Is Bot?", "Message Date", "Sender", "Message", "Text Sentiment", "Emoji Sentiment", "Word Count", "Starter"]
quotedColumns = []
for c in columns:
    quotedColumns.append("\""+c+"\"")
print(",".join(quotedColumns))

thread_index = 1
for item in data:
    threadId = item['thread_id']
    startTimestamp = datetime.fromtimestamp(float(threadId))
    start = startTimestamp.strftime("%Y-%m-%d %H:%M")


    chatHistory = item['history']
    interactionCounter = 1
    for index, chatItem in enumerate(chatHistory):
        isBot = False
        if "bot_id" in chatItem:
            isBot = True
        msgTimestamp = datetime.fromtimestamp(float(chatItem['ts']))
        at = msgTimestamp.strftime("%Y-%m-%d %H:%M")
        user = chatItem['user']
        if user in userMap:
            user = userMap[user]
        else:
            raise ValueError("user {} not found in map".format(user))
        text = chatItem['text']
        
        matches = refUser.findall(text)
        for m in matches:
            text = text.replace("<@"+m+">", userMap[m])
            
        replyText = None
        emojiSentiment=None
        textSentiment=None
        if "slackbot_reply" in chatItem:
            if "text" in chatItem['slackbot_reply']:
                replyText = chatItem['slackbot_reply']['text']
            if 'overall_sentiment_value' in chatItem['slackbot_reply']:
                textSentiment=chatItem['slackbot_reply']['overall_sentiment_value']
            if 'overall_emoji_sentiment_value' in chatItem['slackbot_reply']:
                emojiSentiment=chatItem['slackbot_reply']["overall_emoji_sentiment_value"]
        print("{},{},{},\"{}\",\"{}\",\"{}\",\"{}\",{},{},{},{}".format(thread_index,interactionCounter, index, isBot, at, user, text, textSentiment,emojiSentiment, len(text.split(" ")),1))
        interactionCounter += 1
        if replyText:
            print("{},{},{},\"True\",{},\"Alice\",\"{}\",,,{},{}".format(thread_index,interactionCounter,index,at,replyText,len(replyText.split(" ")),0))
    thread_index += 1
