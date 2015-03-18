# DeFault.Lv.Chat

Single-process, multi-user chat server in PHP running at www.default.lv (was running).

Interesting facts:
 * Single-process serving hundreds of users.
 * Keeping connections open to transfer data back to client.
 * Frontend had two versions: Java applet and JavaScript.
 * JavaScript is comming back from server in open connection when there is something server want's to say.
 * No database used. Plain files.
 * Complex social hierarchy: creadit earning and transfer, user ranks, banning, prison mode and court, and much more. 

Source code at GitHub is missing frontend at the moment. Therefore can be used for educational purposes.

