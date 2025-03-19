import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.sql.*;
import java.text.SimpleDateFormat;
import java.util.Date;
import com.toedter.calendar.JDateChooser;

public class TicketGUI extends JFrame {
    private DefaultTableModel model;
    private JTable table;
    private JTextField eventNameField, locationField, priceField;
    private JDateChooser eventDateChooser; // Utilisation de JDateChooser pour la date
    private JComboBox<String> statusComboBox;

    public TicketGUI() {
        setTitle("🎟️ Gestion des Tickets");
        setSize(700, 500);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLocationRelativeTo(null);
        setLayout(new BorderLayout());

        // Modèle du tableau
        model = new DefaultTableModel();
        table = new JTable(model);
        model.addColumn("ID");
        model.addColumn("Nom de l'événement");
        model.addColumn("Date");
        model.addColumn("Lieu");
        model.addColumn("Prix (€)");
        model.addColumn("Statut");

        loadTickets(); // Charger les tickets

        JScrollPane scrollPane = new JScrollPane(table);
        add(scrollPane, BorderLayout.CENTER);

        // Panel pour les actions
        JPanel panel = new JPanel(new GridLayout(2, 5, 5, 5));

        eventNameField = new JTextField();
        eventDateChooser = new JDateChooser(); // Sélecteur de date
        locationField = new JTextField();
        priceField = new JTextField();
        statusComboBox = new JComboBox<>(new String[]{"National", "International"});

        JButton addButton = new JButton("➕ Ajouter");
        JButton deleteButton = new JButton("🗑️ Supprimer");

        panel.add(new JLabel("Nom:"));
        panel.add(eventNameField);
        panel.add(new JLabel("Date:"));
        panel.add(eventDateChooser);
        panel.add(new JLabel("Lieu:"));
        panel.add(locationField);
        panel.add(new JLabel("Prix:"));
        panel.add(priceField);
        panel.add(new JLabel("Statut:"));
        panel.add(statusComboBox);
        panel.add(addButton);
        panel.add(deleteButton);

        add(panel, BorderLayout.SOUTH);

        // Événement bouton Ajouter
        addButton.addActionListener(e -> addTicket());

        // Événement bouton Supprimer
        deleteButton.addActionListener(e -> deleteTicket());
    }

    private void loadTickets() {
        model.setRowCount(0); // Vider le tableau
        try (Connection conn = DatabaseConnection.getConnection()) {
            String query = "SELECT * FROM tickets";
            Statement stmt = conn.createStatement();
            ResultSet rs = stmt.executeQuery(query);

            while (rs.next()) {
                model.addRow(new Object[]{
                        rs.getInt("id"),
                        rs.getString("event_name"),
                        rs.getString("event_date"),
                        rs.getString("location"),
                        rs.getDouble("price"),
                        rs.getString("status") // Récupération du statut
                });
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void addTicket() {
        String eventName = eventNameField.getText();
        String location = locationField.getText();
        String priceText = priceField.getText();
        Date selectedDate = eventDateChooser.getDate(); // Récupérer la date

        if (eventName.isEmpty() || selectedDate == null || location.isEmpty() || priceText.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Remplissez tous les champs !", "Erreur", JOptionPane.ERROR_MESSAGE);
            return;
        }

        try {
            double price = Double.parseDouble(priceText); // Vérifier si le prix est un nombre

            // Conversion de la date au format MySQL
            SimpleDateFormat outputFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
            String formattedDate = outputFormat.format(selectedDate);

            try (Connection conn = DatabaseConnection.getConnection()) {
                String status = (String) statusComboBox.getSelectedItem(); // Récupération du statut
                
                String query = "INSERT INTO tickets (event_name, event_date, location, price, status) VALUES (?, ?, ?, ?, ?)";
                PreparedStatement stmt = conn.prepareStatement(query);
                stmt.setString(1, eventName);
                stmt.setString(2, formattedDate);
                stmt.setString(3, location);
                stmt.setDouble(4, price);
                stmt.setString(5, status); // Ajout du statut

                stmt.executeUpdate();
                JOptionPane.showMessageDialog(this, "✅ Ticket ajouté avec succès !");
                loadTickets(); // Recharger les tickets
            }
        } catch (NumberFormatException e) {
            JOptionPane.showMessageDialog(this, "❌ Prix invalide ! Entrez un nombre.", "Erreur", JOptionPane.ERROR_MESSAGE);
        } catch (SQLException e) {
            e.printStackTrace();
            JOptionPane.showMessageDialog(this, "❌ Erreur lors de l'ajout du ticket.", "Erreur", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void deleteTicket() {
        int selectedRow = table.getSelectedRow();
        if (selectedRow == -1) {
            JOptionPane.showMessageDialog(this, "Sélectionnez un ticket à supprimer !", "Erreur", JOptionPane.ERROR_MESSAGE);
            return;
        }

        int ticketId = (int) table.getValueAt(selectedRow, 0);
        try (Connection conn = DatabaseConnection.getConnection()) {
            String query = "DELETE FROM tickets WHERE id=?";
            PreparedStatement stmt = conn.prepareStatement(query);
            stmt.setInt(1, ticketId);

            stmt.executeUpdate();
            JOptionPane.showMessageDialog(this, "✅ Ticket supprimé avec succès !");
            loadTickets(); // Recharger les tickets
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> new TicketGUI().setVisible(true));
    }
}
