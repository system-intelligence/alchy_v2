# Sounds Directory

This directory contains audio files (.mp3) used for sound notifications in the chat system.

## Usage

Place your .mp3 sound files here to use them for chat message notifications.

## Example

To play a sound notification in JavaScript:
```javascript
const audio = new Audio('/sounds/notification.mp3');
audio.play();
```

## File Format

- Only .mp3 files are supported
- Keep file sizes reasonable for web performance
- Use descriptive filenames