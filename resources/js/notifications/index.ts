import { Notify } from 'quasar'
import apolloClient from "../apollo";
import trainingUpdatedSubscription from "../queries/subscriptions/trainingupdated.subscription.gql";


export function useNotifications() {

  function subscribeToTrainingUpdates() {
      const subscription = apolloClient.subscribe({
          query: trainingUpdatedSubscription
      }).subscribe({
          next: (data) => {
              if (data.trainingUpdated) {
                  Notify.create({
                      message: "Training mit ID " + data.trainingUpdated.id + " wurde aktualisiert.",
                      color: "positive",
                      position: "top",
                  });
              }
          },
          error: (err) => {
              console.error('Error on subscription', err)
          }
      });
      console.log(subscription)

  }

    return {subscribeToTrainingUpdates};
}
