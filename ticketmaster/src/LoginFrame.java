import javax.swing.*;

public class LoginFrame extends JFrame {
    public LoginFrame() {
        // Titre de la fenêtre
        setTitle("Login - TicketMaster");
        setSize(400, 300);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLocationRelativeTo(null);

        // Création des composants
        JLabel userLabel = new JLabel("Nom d'utilisateur:");
        JTextField userText = new JTextField(20);
        JLabel passLabel = new JLabel("Mot de passe:");
        JPasswordField passText = new JPasswordField(20);
        JButton loginButton = new JButton("Connexion");

        // Layout
        JPanel panel = new JPanel();
        panel.add(userLabel);
        panel.add(userText);
        panel.add(passLabel);
        panel.add(passText);
        panel.add(loginButton);

        // Ajout du panel à la fenêtre
        add(panel);

        // Rendre visible
        setVisible(true);
    }

    public static void main(String[] args) {
        new LoginFrame();
    }
}
