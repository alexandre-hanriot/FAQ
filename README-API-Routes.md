# Les routes de l'API

| Routes | Nom de la route | Méthodes (HTTP) | Controller | Methodes() | HTTP Status |
|---|---|---|---|---|---|
|QUESTIONS
| /api/questions | api_questions_get | GET | App\Controller\Api\QuestionController | getQuestions() | 200 |
| /api/questions/{id<\d+>} | api_questions_show | GET | App\Controller\Api\QuestionController | showQuestion() | 200 |
| /api/questions | api_questions_add | POST + JSON | App\Controller\Api\QuestionController | addQuestion() | 201 + Location: questions/{id}|
|ANSWER
| /api/questions/{id}/answers | api_answers_add | POST + JSON| App\Controller\Api\AnswerController | addAnswer() | 201 + Location: questions/{id} |
|TAGS
| /api/tags | api_tags_get | GET | App\Controller\Api\TagController | getTags() | 200 |
| /api/questions/tags/{id} | api_questions_by_tag | GET | App\Controller\Api\QuestionController | getQuestionsByTag() |200

