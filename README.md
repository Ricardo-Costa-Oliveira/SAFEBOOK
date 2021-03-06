Safebook
======
Este projeto aponta para o desenvolvimento de uma plataforma para publicar mensagens
online tipo Twitter. A maior diferença é que as mensagens são publicadas cifradas, e só
depois do recetor dessas mensagens as decifrar e ver, é que são disponibilizadas em
texto limpo. Por outras palavras, cada utilizador do sistema deverá ter a possibilidade de
publicar uma mensagem direcionada para outro utilizador, e só quando esse utilizador
ler essa mensagem é que esta aparece em texto limpo no site. Todas as mensagens
devem ser assinadas digitalmente pelo autor original das mesmas e, após serem lidas
pelo destinatário, também devem conter uma assinatura deste último. As funcionalidades
básicas da plataforma são:

1. registo de um novo utilizador

1. cifragem, assinatura digital e publicação de mensagens

1. decifragem de mensagens direcionadas ao utilizador no programa cliente

1. verificação da assinatura digital das mensagens decifradas

1. notificação de nova mensagem

___

A natureza desta plataforma define implicitamente dois programas:

1. Um servidor que gere as informações de todos os utilizadores (tipo username e passwords) e publica as mensagens

1. e um cliente, que produz assinaturas digitais, cifra ou decifra mensagens (dependendo do papel que o seu utilizador está a tomar nesse momento), e que se liga ao
servidor para publicar mensagens.

___

Note-se que a ideia princípal deste projeto é a de que esta plataforma faça uso de mecanismos de criptografia simétricas e assimétrica. Isto é, quando determinado utilizador quiser publicar uma mensagem, deve escolher (interativamente) o destinatário da mesma, pedir ao servidor que lhe envie a chave pública (ou o certificado) desse destinatário e cifrar a mensagem com essa chave. O destinatário deve posteriormente receber uma notificação de nova mensagem recebida, decifrá-la com a sua chave privada e, caso concorde, substituir o texto cifrado publicado pelo texto limpo que decifrou.

___

Podem fortalecer o trabalho e conhecimento através da implementação das seguintes funcionalidades:

1. ter um help bastante completo

1. Usar certificados digitais X.509, e implementar uma infraestrutura de chave pública para o sistema e validar cadeias de certificados

1. Pensar numa forma correta de fornecer certificados digitais a utilizadores

1. Permitir escolher entre dois ou mais algoritmos de cifra e funções de hash

1. Adicionar outros serviços, e.g., envio de uma mensagem para vários utilizadores (ao invés de um só)

___

Pensem numa forma de atacar o sistema (uma falha da sua implementação) e dediquem-
lhe uma secção no relatório.
