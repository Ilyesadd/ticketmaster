import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class TicketDAO {
    public static void afficherTickets() {
        Connection conn = DatabaseConnection.getConnection();

        if (conn == null) {
            System.out.println("❌ Impossible de récupérer la connexion !");
            return;
        }

        String query = "SELECT * FROM tickets";

        try {
            Statement stmt = conn.createStatement();
            ResultSet rs = stmt.executeQuery(query);

            System.out.println("🎟️ Liste des tickets disponibles :");
            while (rs.next()) {
                int id = rs.getInt("id");
                String eventName = rs.getString("event_name");
                String date = rs.getString("event_date");
                String location = rs.getString("location");
                double price = rs.getDouble("price");

                System.out.println(id + " | " + eventName + " | " + date + " | " + location + " | " + price + "€");
            }

            stmt.close();
            conn.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
